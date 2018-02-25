import React, { Component } from 'react'
import { Avatar } from 'antd'
// import moment from 'moment'
import TimeChange from 'utils/format'
import fetchPost from 'utils/fetch'
//视频详情页 - 右上半部分
class DetailDllist extends Component{
  constructor(props){
    super(props)
    this.state={
      Dllist:[]
    }
  }
  onShowActive(active){
    // 0表示未审批，1表示正常，2表示屏蔽,3表自己删除，4待定，5平台删除
     switch(active){
      case 0:  return "未审批"
        break
      case 1:  return "正常"
        break
      case 2:  return "屏蔽"
        break
      case 3:  return "自己删除"
        break
      case 4:  return "待定"
        break
      case 5:  return "平台删除"
        break
      default: return "未审批"
     }
  }

  GetListData(){
    fetchPost.post({
      uri:`/api/admins/video/details/${this.props.id}`,
      callback:(res)=>{
        this.setState({
          Dllist:res.tweets_data
        })
      }
    })
  }


  render(){
    return(
        <dl className="detail_list">
          <dt>
            <Avatar style={{width:46,height:46,borderRadius: "100%"}} src="https://zos.alipayobjects.com/rmsportal/ODTLcjxAfvqbxHnVXCYX.png" />
            <Avatar style={{width:14,height:14,lineHeight:"14px",borderRadius: "100%"}} src="https://zos.alipayobjects.com/rmsportal/ODTLcjxAfvqbxHnVXCYX.png" />
          </dt>
          <dd>
            <h3>2017快男全国晋级赛</h3>
            <p>
              <span>{TimeChange.formatStamp(this.state.Dllist.created_at)}</span>
              <span>播放 : {this.state.Dllist.browse_times}</span>
              <span>来源 : {this.state.Dllist.phone_type}</span>
              <span>系统 : {this.state.Dllist.phone_os}</span>
            </p>
            <p>
              <span>尺寸 : {this.state.Dllist.shot_width_height}</span>
              <span>大小 : 23mb</span>
              <span>时长 : {TimeChange.formatSec(1216)}</span>
            </p>
            <p><span>地理位址 : 北京.朝阳区.三里屯街道</span></p>
            <p>
              <span>URL :
                <a style={{color:"#108ee9",marginLeft:"5px"}}>{"http://www.hivideo.com/video/1239801"}</a>
              </span>
            </p>
            <p>参与竞赛 : <span style={{color:"#108ee9"}}>{this.state.Dllist.activity}</span></p>
            <p>关键词 :  <span><b>黄子韬,4强诞生</b> </span>
            </p>
            {
              (this.props.type == "videoCensor") ?
                (<p>状态 : <b>{this.onShowActive(this.state.Dllist.active)}</b></p>) :
                (<p>操作员：GOOBIRD001     操作日期：2017-08-09  18:21   状态：屏蔽</p>)
            }
            <p className="detail_brief" >{this.state.Dllist.content}</p>
          </dd>
        </dl>
    )
  }
  componentDidMount(){
    this.GetListData()
  }
  componentWillReceiveProps(nextPorps){
    this.GetListData()
  }

}


export default DetailDllist
