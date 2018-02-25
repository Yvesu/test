import React, {Component} from 'react'
import { Link } from 'react-router'
import { Button, Modal, Spin, Icon } from 'antd'

import DetailBtn from './DetailBtn'
import DetailDllist from './DetailDllist'
import DetailTabs from './DetailTabs'
import TabsPage from './TabsPage'
import FetchPost from 'utils/fetch'

class Detail extends Component {
  constructor(props) {
    super(props)
    this.state={
      videoData:[],
      loading:false,
      PrevDisabled:false,
      NextDisabled:false,
      nextId:"",
      prevId:"",
      playIcon:true,
      showPlay:true
    }
  }



  handleChangeVideo=()=>{
    const onControls = this.refs.videoControls
    if(onControls.paused){
      this.setState({
        playIcon:false,
        // showPlay:false
      })
      onControls.play()
    }else{
      onControls.pause()
      this.setState({
        playIcon:true,
        // showPlay:false
      })
    }
  }

  handleChangeDetailVideoInit=()=>{
    const onControls = this.refs.videoControls
    if(this.state.playIcon === false){
      this.setState({
        playIcon:true,
      })
    }
    onControls.load()
  }

  GoToNextDetail(videoData){
    if(this.state.nextId != ""){
      this.setState({ loading: true })
      this.props.router.push(`/${this.props.params.type}/detail/${this.state.nextId}`)
    }else {
      this.setState({NextDisabled:true})
      Modal.info({
         title: 'Info message',
         content: '已经是最后一个了',
         okText:'ok'
      })
    }
  }


  GoToPrevDetail(){
    if(this.state.prevId != ""){
      this.setState({ loading: true })
      this.props.router.push(`/${this.props.params.type}/detail/${this.state.prevId}`)
    }else {
      this.setState({PrevDisabled:true})
      Modal.info({
         title: 'Info message',
         content: '已经是第一个了',
         okText:'ok'
       })
    }
  }
  handleDetail=()=>{
    this.setState({ loading: true })
    FetchPost.post({
      uri:`/api/admins/video/details/${this.props.params.id}`,
      callback:(res)=>{
        // console.log(res,'res');
        this.setState({
          loading:false,
          videoData:res.tweets_data,
          nextId:res.next_id,
          prevId:res.prev_id
        })
      }
    })
  }


  render(){
    if(this.state.videoData) {
      return(
        <Spin spinning={this.state.loading}>
          <div style={{padding: 15, margin: 0,minHeight:900, background:"#fff"}}>
             <div className="Detail_left">
                <div className="detail_video_box">
                  <video className="video_play" controls preload={"auto"} ref="videoControls"
                    onClick={this.handleChangeVideo}
                    poster={`${this.state.videoData.screen_shot}?imageMogr2/thumbnail/450x`}
                    src={this.state.videoData.video} >
                  </video>
                  <div className="video_play_box_mask" onClick={this.handleChangeVideo}>
                    <Icon type={this.state.playIcon===true?"caret-right" : "pause" } />
                  </div>
                </div>
                <DetailBtn type={this.props.params.type} id={this.props.params.id }
                    GoToNextDetail={this.GoToNextDetail.bind(this)}
                    GoToPrevDetail={this.GoToPrevDetail.bind(this)}
                    NextDisabled={this.state.NextDisabled}
                    PrevDisabled={this.state.PrevDisabled}
                    shotImg={this.state.videoData.screen_shot}
                  />
             </div>
             <div className="Detail_right">
                <DetailDllist type={this.props.params.type} id={this.props.params.id}/>
                <DetailTabs  id={this.props.params.id} />
                <TabsPage />
             </div>
          </div>
        </Spin>
      )
     }else{
        return (
          <div className="NoDetail">
            <div className="No_box">
                <img src="./404.png" />
                <p>抱歉！您所访问的页面不存在！！！</p>
            </div>
          </div>
        )}
  }
  componentDidMount(){
      this.handleDetail()
  }
  componentWillReceiveProps(nextPorps){
    // console.log(nextPorps,'hhah');
    this.handleDetail()
    this.handleChangeDetailVideoInit()

  }

}

export default Detail

  //加载
  // <Spin spinning={this.state.loading}>
  // </Spin>
