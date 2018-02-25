import React, { Component } from 'react'
import { Button, BackTop  } from 'antd'
import Fetch from 'utils/fetch'
class RaceList extends Component{
  constructor(props){
    super(props)
    this.state={
      data:[],
      loadingMore:false,
      page:1,
      more:true
    }
  }
  handleGetRaceListData=()=>{
    // console.log(this.state.loadingMore,"加载更多");
    let formData = new FormData()
    if(this.props.active){
      formData.append('active',this.props.active)
    }
    if(this.state.loadingMore===true){
      const page = ++this.state.page
      // console.log(page,"page");
      formData.append('page',page)
    }
    Fetch.post({
      uri:this.props.uri,
      callback:(res)=>{
        // console.log(res,'返回的数据');
        this.setState({
          data:res.data,
          more:res.more
        })
        if(this.state.loadingMore===true){
          this.state.loadingMore=false
        }
      },
      formData:formData
    })
  }
  handleGetLoadingMoreData=()=>{
    this.state.loadingMore=true
    this.handleGetRaceListData()
  }

  render(){
    return(
      <div className="race_list_box">
        {
          this.state.data.map((value,index)=>{
            return(
              <div className="ract_list_only" key={index}>
                <div className='list_only_title'>
                  <h2>
                    <span>{value.name}</span>
                    <span style={{display:value.end? 'block' : 'none'}}>已结束</span>
                  </h2>
                  <Button className={value.end? 'race_is_end' : ''}>
                    {this.props.raceBtnText? this.props.raceBtnText : '提交作品'}
                  </Button>
                </div>
                <div className='list_only_info'>
                  <p>
                    <span>提交截止</span>
                    <span>节日期</span>
                    <span>费用</span>
                  </p>
                  <p>
                    <span>{value.submit_end_time}</span>
                    <span>{value.festTime}</span>
                    <span>{value.cost===0? '无' : value.cost }</span>
                  </p>
                  <p>
                    <span>接受类别</span>
                    <span>举办地</span>
                  </p>
                  <p>
                    <span>{value.type}</span>
                    <span>{value.address}</span>
                  </p>
                </div>
              </div>
            )
          })
        }
        <p className='race_loading_more_btn'>
          {
            this.state.more !==true? <span className="no_loading_more">没有更多了...</span>
            : <Button onClick={this.handleGetLoadingMoreData} >
                加载更多
              </Button>
          }
        </p>
        <BackTop />
      </div>
    )
  }
  componentDidMount(){
    if(this.props.uri){
      this.handleGetRaceListData()
    }
  }
}

export default RaceList
