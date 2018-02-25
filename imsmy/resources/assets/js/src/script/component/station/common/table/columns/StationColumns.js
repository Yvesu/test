//站务中的表格标题
import React from 'react'
import { Link } from 'react-router'
import { Table,Icon, Button } from 'antd'
import CensorMenu from '../dropdownMenu/CensorMenu'
import PendingMenu from '../dropdownMenu/PendingMenu'
import ScreenMenu from '../dropdownMenu/ScreenMenu'
import TimeChange from 'utils/format'

//视频审查
export const videoCensor = [
  { title: '评分', dataIndex: '评分', key: 'id', width:"7%",
    render:(text,record) =>(<b>8.0分</b>)
  },
  { title: '类型', dataIndex: 'type', key:'type', width:"9%",
    render:(text,record) =>(<b>{record.type}</b>)
  },
  { title: '封面', dataIndex: 'screen_shot',key: 'screen_shot',  width:"10.7%",
    render:(text,record) =>(
      <Link to={`/videoCensor/detail/${record.id}`}>
        <div className="cover_box">
          <img className="detail_img_hover"
            src={`${record.screen_shot}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}/>
            <div className="detail_img_mask">
              <Icon type="play-circle"  style={{fontSize:"18px",color:"#fff"}}/>
            </div>
        </div>
      </Link>
    )
  },
  { title: '描述', dataIndex: '描述', key: '描述', width:"16.4%",
    render:(text,record) => ("对视频的描述")
  },
  { title: '时长', dataIndex: 'duration', key:'duration', width:"7%",
    render:(text,record,index) =>(<b>{TimeChange.formatSec(record.duration)}</b>)
  },
  { title:  "上传日期", dataIndex: "上传日期" , key: "time",width:"14%",
    render:(text,record) =>( "2017-09-20 11:16:28")
  },
  { title: "来自", dataIndex:  "来自" , key: "来自" ,width:"11%",  width:"14%",
    render:(text,record) =>( <b>2017快男全国晋级赛</b>)
  },
  { title: "播放", dataIndex: 'bofang', key: 'bofang', width:"14%",
    render:(text,record) =>{record.browse_times}
  },
  { title: '操作', dataIndex: '操作', key: 'more',
    render:(text,record,index) =>(
      <CensorMenu record={record.id} operation/>)
  }
]
//待定池
export const videoPending = [
  { title: '评分', dataIndex: '评分', key: 'id', width:"5%",
    render:(text,record) =>(<b>8.0分</b>)
  },
  { title: '类型', dataIndex: 'type', key:'type', width:"7.5%",
    render:(text,record) =>(<b>{record.type}</b>)
  },
  { title: '封面', dataIndex: 'screen_shot',key: 'screen_shot',  width:"10.7%",
    render:(text,record) =>(
      <Link to={`/videoPending/detail/${record.id}`}>
        <div className="cover_box">
          <img className="detail_img_hover"
            src={`${record.screen_shot}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}/>
            <div className="detail_img_mask">
              <Icon type="play-circle"  style={{fontSize:"18px",color:"#fff"}}/>
            </div>
        </div>
      </Link>
    )
  },
  { title: '描述', dataIndex: '描述', key: '描述', width:"16.4%",
    render:(text,record) => ("对视频的描述")
  },
  { title: '时长', dataIndex: 'duration', key:'duration', width:"7%",
    render:(text,record,index) =>(<b>{TimeChange.formatSec(record.duration)}</b>)
  },
  { title:  "操作日期", dataIndex: "操作日期" , key: "time",width:"14%",
    render:(text,record) =>(`${TimeChange.formatStamp(record.undetermined_time)}`)
  },
  { title: "操作员", dataIndex:  "操作员" , key: "操作员" , width:"14%",
    render:(text,record) =>(<b>{record.undetermined_user}</b>)
  },
  { title: "播放", dataIndex: 'bofang', key: 'bofang', width:"14%",
    render:(text,record) =>(`${record.browse_times}`)
  },
  { title: '操作', dataIndex: '操作', key: 'more',
    render:(text,record,index) =>(
      <PendingMenu record={record.id} operation />)
  }
]
//屏蔽仓
export const videoScreen = [
  { title: '评分', dataIndex: '评分', key: 'id', width:"5%",
    render:(text,record) =>(<b>8.0分</b>)
  },
  { title: '类型', dataIndex: 'type', key:'type', width:"7.5%",
    render:(text,record) =>(<b>{record.type}</b>)
  },
  { title: '封面', dataIndex: 'screen_shot',key: 'screen_shot',  width:"10.7%",
    render:(text,record) =>(
      <Link to={`/videoScreen/detail/${record.id}`}>
        <div className="cover_box">
          <img className="detail_img_hover"
            src={`${record.screen_shot}?imageMogr2/thumbnail/128x/gravity/Center/crop/128x72`}/>
            <div className="detail_img_mask">
              <Icon type="play-circle"  style={{fontSize:"18px",color:"#fff"}}/>
            </div>
        </div>
      </Link>
    )
  },
  { title: '描述', dataIndex: '描述', key: '描述', width:"16.4%",
    render:(text,record) => ("对视频的描述")
  },
  { title: '时长', dataIndex: 'duration', key:'duration', width:"7%",
    render:(text,record,index) =>(<b>{TimeChange.formatSec(record.duration)}</b>)
  },
  { title:  "操作日期", dataIndex: "操作日期" , key: "time",width:"14%",
    render:(text,record) =>(`${TimeChange.formatStamp(record.forbid_time)}`)
  },
  { title: "操作员", dataIndex:  "操作员" , key: "操作员" , width:"14%",
    render:(text,record) =>(<b>{record.forbid_user}</b>)
  },
  { title: "屏蔽理由", dataIndex: '屏蔽理由', key: '屏蔽理由', width:"14%",
    render:(text,record) =>(`${record.forbid_reason}`)
  },
  { title: '操作', dataIndex: '操作', key: 'more',
    render:(text,record,index) =>(
      <ScreenMenu record={record.id} shotImg={record.screen_shot} operation />)
  }
]
